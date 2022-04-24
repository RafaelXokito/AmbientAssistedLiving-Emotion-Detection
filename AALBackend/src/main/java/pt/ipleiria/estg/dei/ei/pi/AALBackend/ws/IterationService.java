package pt.ipleiria.estg.dei.ei.pi.AALBackend.ws;

import javax.ejb.EJB;
import javax.ws.rs.*;
import javax.ws.rs.core.MediaType;
import javax.ws.rs.core.Response;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.ClientDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.FrameDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.IterationDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.IterationBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Frame;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Iteration;
import java.util.List;
import java.util.stream.Collectors;

@Path("iterations")
@Produces({MediaType.APPLICATION_JSON})
@Consumes({MediaType.APPLICATION_JSON})
public class IterationService {
    
    @EJB
    private IterationBean iterationBean;


    @GET
    @Path("/")
    public Response getAllIterationsWS() {
        return Response.status(Response.Status.OK)
                .entity(toDTOs(iterationBean.getAllIterations()))
                .build();
    }

    @GET
    @Path("{id}")
    public Response getIterationWS(@PathParam("id") Long id) throws Exception {
        Iteration iteration = iterationBean.findIteration(id);

        return Response.status(Response.Status.OK)
                .entity(toDTO(iteration))
                .build();
    }

    /*
    It's not possible to create or delete iterations (JUST DELETE)
    @POST
    @Path("/")
    public Response createIterationWS(IterationDTO iterationDTO) throws Exception {
        iterationBean.create(iterationDTO.getMacAddress(), iterationDTO.getClient().getEmail());

        Iteration iteration = iterationBean.findIteration(iterationDTO.getId());
        return Response.status(Response.Status.CREATED)
                .entity(toDTO(iteration))
                .build();
    }

    @DELETE
    @Path("{id}")
    public Response deleteIterationWS(@PathParam("id") Long id) throws Exception {
        if (iterationBean.delete(id))
            return Response.status(Response.Status.OK)
                    .entity("[Success] Iteration with id \'"+id+"\' was deleted")
                    .build();

        return Response.status(Response.Status.INTERNAL_SERVER_ERROR)
                .build();
    }*/


    private List<IterationDTO> toDTOs(List<Iteration> iterations) {
        return iterations.stream().map(this::toDTO).collect(Collectors.toList());
    }

    private IterationDTO toDTO(Iteration iteration) {
        return new IterationDTO(
            iteration.getId(),
            iteration.getMacAddress(),
            clientToDTO(iteration.getClient()),
            framesToDTOs(iteration.getFrames())
        );
    }

    FrameDTO frameToDTO(Frame frame) { return new FrameDTO(
        frame.getId(),
        frame.getName(),
        frame.getPath());
    }

    List<FrameDTO> framesToDTOs(List<Frame> frames) {
        return frames.stream().map(this::frameToDTO).collect(Collectors.toList());
    }

    private ClientDTO clientToDTO(Client client) {
        return new ClientDTO(
            client.getEmail(),
            client.getName(),
            client.getAge(),
            client.getContact()
        );
    }
}
