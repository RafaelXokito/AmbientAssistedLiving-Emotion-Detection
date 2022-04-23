package pt.ipleiria.estg.dei.ei.pi.AALBackend.ws;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.ClientDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.ClientBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;

import javax.ejb.EJB;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;
import java.util.List;
import java.util.stream.Collectors;

@Path("clients")
@Produces({MediaType.APPLICATION_JSON})
@Consumes({MediaType.APPLICATION_JSON})
public class ClientService {

    @EJB
    private ClientBean clientBean;

    @GET
    @Path("/")
    public Response getAllClientsWS() {
        return Response.status(Response.Status.OK)
                .entity(toDTOs(clientBean.getAllCLients()))
                .build();
    }

    @GET
    @Path("{email}")
    public Response getClientWS(@PathParam("email") String email) throws Exception {
        Client cLient = clientBean.findClient(email);

        return Response.status(Response.Status.OK)
                .entity(toDTO(cLient))
                .build();
    }

    @POST
    @Path("/")
    public Response createClientWS(ClientDTO clientDTO) throws Exception {
        clientBean.create(clientDTO.getEmail(), clientDTO.getPassword(), clientDTO.getName(), clientDTO.getAge(), clientDTO.getContact());

        Client client = clientBean.findClient(clientDTO.getEmail());
        return Response.status(Response.Status.CREATED)
                .entity(toDTO(client))
                .build();
    }

    @PUT
    @Path("{email}")
    public Response updateClientWS(@PathParam("email") String email,ClientDTO clientDTO) throws Exception {
        clientBean.update(email, clientDTO.getName(), clientDTO.getAge(), clientDTO.getContact());

        Client client = clientBean.findClient(email);

        return Response.status(Response.Status.OK)
                .entity(toDTO(client))
                .build();
    }

    @DELETE
    @Path("{email}")
    public Response deleteClientWS(@PathParam("email") String email) throws Exception {
        if (clientBean.delete(email))
            return Response.status(Response.Status.OK)
                    .entity("[Success] Client with email \'"+email+"\' was deleted")
                    .build();

        return Response.status(Response.Status.INTERNAL_SERVER_ERROR)
                .build();
    }


    private List<ClientDTO> toDTOs(List<Client> clients) {
        return clients.stream().map(this::toDTO).collect(Collectors.toList());
    }

    private ClientDTO toDTO(Client client) {
        return new ClientDTO(
                client.getEmail(),
                client.getPassword(),
                client.getName(),
                client.getAge(),
                client.getContact()
                );
    }
}
