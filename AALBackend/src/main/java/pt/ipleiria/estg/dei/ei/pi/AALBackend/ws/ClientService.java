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
    @Path("{id}")
    public Response getClientWS(@PathParam("id") Long id) throws Exception {
        Client cLient = clientBean.findClient(id);

        return Response.status(Response.Status.OK)
                .entity(toDTO(cLient))
                .build();
    }

    @POST
    @Path("/")
    public Response createClientWS(ClientDTO clientDTO) throws Exception {
        clientBean.create(clientDTO.getEmail(), clientDTO.getPassword(), clientDTO.getName(), clientDTO.getAge(), clientDTO.getContact());

        Client client = clientBean.findClient(clientDTO.getId());
        return Response.status(Response.Status.CREATED)
                .entity(toDTO(client))
                .build();
    }

    @PUT
    @Path("{id}")
    public Response updateClientWS(@PathParam("id") Long id,ClientDTO clientDTO) throws Exception {
        clientBean.update(id, clientDTO.getName(), clientDTO.getAge(), clientDTO.getContact());

        Client client = clientBean.findClient(id);

        return Response.status(Response.Status.OK)
                .entity(toDTO(client))
                .build();
    }

    @DELETE
    @Path("{id}")
    public Response deleteClientWS(@PathParam("id") Long id) throws Exception {
        if (clientBean.delete(id))
            return Response.status(Response.Status.OK)
                    .entity("[Success] Client with id \'"+id+"\' was deleted")
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
